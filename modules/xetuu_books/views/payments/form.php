<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<style>
/* Remove the generic wrapper padding so the form fills edge-to-edge */
.xb-content-wrapper { padding: 0 !important; }

/* Top chrome */
.xb-inv-topbar { background: white; padding: 16px 24px; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 10; }
.xb-inv-back { color: #16a34a; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; font-size: 14px; margin-right: 16px; transition: color 0.2s; }
.xb-inv-back:hover { color: #15803d; text-decoration: none; }
.xb-inv-back svg { width: 18px; height: 18px; fill: currentColor; margin-right: 6px; }
.xb-inv-title { font-size: 18px; font-weight: 700; color: #111827; display: inline-flex; align-items: center; }

.xb-inv-actions .btn { border-radius: 6px; padding: 8px 16px; font-weight: 600; font-size: 13px; box-shadow: 0 1px 2px rgba(0,0,0,.05); transition: all 0.2s; }
.xb-inv-actions .btn-default { border: 1px solid #d1d5db; color: #374151; background: white; }
.xb-inv-actions .btn-default:hover { background: #f9fafb; border-color: #9ca3af; }
.xb-inv-actions .btn-success { background: #16a34a; border: 1px solid #16a34a; color: white; }
.xb-inv-actions .btn-success:hover { background: #15803d; border-color: #15803d; }

.xb-inv-body { padding: 24px; background: #f9fafb; min-height: calc(100vh - 150px); }
.xb-inv-paper { background: white; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -1px rgba(0,0,0,.06); border: 1px solid #e5e7eb; padding: 32px; max-width: 800px; margin: 0 auto; }

.form-group label { font-weight: 600; color: #374151; font-size: 13px; margin-bottom: 6px; }
.form-control { border-radius: 6px; border: 1px solid #d1d5db; padding: 10px 12px; font-size: 14px; box-shadow: none; transition: border-color 0.15s, box-shadow 0.15s; }
.form-control:focus { border-color: #16a34a; box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1); }
</style>

<?php 
$is_vendor = ($partner_type === 'vendor');
$list_url  = admin_url('xetuu_books/' . ($is_vendor ? 'vendor_payments' : 'payments'));
$action_url = admin_url('xetuu_books/' . ($is_vendor ? 'vendor_payment' : 'payment'));
?>

<div class="xb-inv-topbar">
    <div style="display:flex;align-items:center;">
        <a href="<?php echo $list_url; ?>" class="xb-inv-back">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
            <?php echo $is_vendor ? 'Vendor Payments' : 'Customer Payments'; ?>
        </a>
        <span class="xb-inv-title">
            <i class="fa fa-money" style="color:#16a34a;margin-right:6px;"></i>
            New <?php echo $is_vendor ? 'Vendor' : 'Customer'; ?> Payment
        </span>
    </div>
    <div class="xb-inv-actions">
        <a href="<?php echo $list_url; ?>" class="btn btn-default mr-2">Cancel</a>
        <button type="submit" form="payment-form" class="btn btn-success">Save Payment</button>
    </div>
</div>

<div class="xb-inv-body">
    <div class="xb-inv-paper">
        <?php echo form_open($action_url, ['id' => 'payment-form']); ?>
        
        <div class="row">
            <!-- Partner -->
            <div class="col-md-6 form-group">
                <label for="partner_id"><?php echo $is_vendor ? 'Vendor' : 'Customer'; ?></label>
                <select name="partner_id" id="partner_id" class="selectpicker" data-width="100%" data-live-search="true" required>
                    <option value=""></option>
                    <?php foreach ($partners as $partner): ?>
                        <?php if ($is_vendor): ?>
                            <option value="<?php echo $partner->vendor_id ?? $partner->userid; ?>">
                                <?php echo htmlspecialchars($partner->vendor_name ?? $partner->company); ?>
                            </option>
                        <?php else: ?>
                            <option value="<?php echo $partner->userid; ?>">
                                <?php echo htmlspecialchars($partner->company); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Amount -->
            <div class="col-md-6 form-group">
                <label for="amount">Amount</label>
                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required min="0.01">
            </div>
        </div>

        <div class="row" style="margin-top: 15px;">
            <!-- Journal -->
            <div class="col-md-6 form-group">
                <label for="journal_id">Payment Journal</label>
                <select name="journal_id" id="journal_id" class="selectpicker" data-width="100%" required>
                    <option value=""></option>
                    <?php foreach ($payment_journals as $journal): ?>
                        <option value="<?php echo $journal->id; ?>"><?php echo htmlspecialchars($journal->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div class="col-md-6 form-group">
                <label for="date">Payment Date</label>
                <div class="input-group date">
                    <input type="text" name="date" id="date" class="form-control datepicker" value="<?php echo _d(date('Y-m-d')); ?>" required>
                    <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12 form-group">
                <label for="memo">Memo / Reference</label>
                <input type="text" name="memo" id="memo" class="form-control" placeholder="e.g. Check #12345">
            </div>
        </div>

        <?php if (function_exists('xb_render_analytic_field')): ?>
        <div class="row" style="margin-top: 15px;">
            <div class="col-md-12">
                <?php echo xb_render_analytic_field($is_vendor ? 'vendor_payment' : 'customer_payment', 0, 'Analytic Account (Cost Centre)'); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php echo form_close(); ?>
    </div>
</div>

<script>
    $(function(){
        init_datepicker();
        appValidateForm($('#payment-form'), {
            partner_id: 'required',
            amount: 'required',
            journal_id: 'required',
            date: 'required'
        });
    });
</script>
