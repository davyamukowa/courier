<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$is_edit   = isset($contract) && $contract;
$title_str = $is_edit ? 'Edit Contract — ' . htmlspecialchars($contract->contract_number) : 'New Purchase Contract';
$save_url  = admin_url('xetuu_books/save_purchase_contract_form' . ($is_edit ? '/' . $contract->id : ''));
$is_signed = $is_edit && (int)$contract->signed === 1;
$stage_num = $is_signed ? 3 : ($is_edit ? 2 : 1);
$stages    = ['Draft', 'Sent', 'Signed'];
?>

<div class="xb-form-page">
  <div class="xb-form-header">
    <div class="xb-form-header-left">
      <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i></a>
      <div>
        <p class="xb-form-breadcrumb">
          <a href="<?= admin_url('xetuu_books') ?>">Xetuu Books</a> <span>/</span>
          <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>">Contracts</a> <span>/</span>
          <?= $is_edit ? htmlspecialchars($contract->contract_number) : 'New' ?>
        </p>
        <h1 class="xb-form-title"><?= $title_str ?></h1>
      </div>
    </div>
    <div class="xb-form-actions">
      <?php if ($is_edit && !$is_signed): ?>
      <a href="<?= admin_url('xetuu_books/sign_purchase_contract/' . $contract->id) ?>"
         class="btn btn-success btn-sm" onclick="return confirm('Sign this contract as yourself?')">
        <i class="fa fa-pencil"></i> Sign Contract
      </a>
      <?php endif; ?>
      <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>" class="btn btn-default">Cancel</a>
      <?php if (!$is_signed): ?>
      <button type="submit" form="pur-con-form" class="xb-save-btn"><i class="fa fa-floppy-o"></i> Save Contract</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="xb-stage-bar">
    <div class="xb-stages">
      <?php foreach ($stages as $i=>$lbl): $n=$i+1; $cls=$n<$stage_num?'done':($n==$stage_num?'active':''); ?>
      <div class="xb-stage <?=$cls?>">
        <div class="xb-stage-wrap">
          <div class="xb-stage-dot"><?=$n<$stage_num?'✓':$n?></div>
          <div class="xb-stage-label"><?=$lbl?></div>
        </div>
        <?php if ($i<count($stages)-1): ?><div class="xb-stage-line"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?= form_open($save_url, ['id'=>'pur-con-form']) ?>
  <?php if ($is_signed): ?><input type="hidden" name="_readonly" value="1"><?php endif; ?>
  <div class="xb-form-body">
    <div class="xb-form-main">
      <div class="xb-form-tabs"><ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#con-details">Details</a></li>
        <li><a data-toggle="tab" href="#con-terms">Terms &amp; Content</a></li>
        <?php if ($is_signed): ?><li><a data-toggle="tab" href="#con-sig">Signature</a></li><?php endif; ?>
      </ul></div>

      <?php if ($is_signed): ?>
      <div class="alert alert-success" style="margin-bottom:14px;">
        <i class="fa fa-check-circle"></i>
        <strong>This contract has been signed</strong> by <?= htmlspecialchars($contract->signer_name ?? 'a staff member') ?>
        on <?= _d($contract->signed_date) ?>. It is now read-only.
      </div>
      <?php endif; ?>

      <div class="tab-content">
        <div class="tab-pane active" id="con-details">
          <div class="xb-fcard"><div class="xb-fcard-header">Contract Information</div><div class="xb-fcard-body">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Contract Name <span class="req">*</span></label>
                  <input type="text" name="contract_name" class="form-control" required
                         <?= $is_signed ? 'readonly' : '' ?>
                         placeholder="e.g. Annual Supplier Agreement 2026"
                         value="<?= $is_edit ? htmlspecialchars($contract->contract_name) : '' ?>">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Vendor <span class="req">*</span></label>
                  <select name="vendor" class="form-control" required <?= $is_signed ? 'disabled' : '' ?>>
                    <option value="">— Select Vendor —</option>
                    <?php foreach ($vendors as $v): ?>
                    <option value="<?=$v->userid?>" <?= $is_edit && $contract->vendor==$v->userid?'selected':'' ?>><?= htmlspecialchars($v->company) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <?php if ($is_signed): ?><input type="hidden" name="vendor" value="<?= $contract->vendor ?>"> <?php endif; ?>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">Contract Value</label>
                  <input type="number" name="contract_value" class="form-control" step="0.01" min="0"
                         <?= $is_signed ? 'readonly' : '' ?>
                         value="<?= $is_edit ? $contract->contract_value : '' ?>" placeholder="0.00">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">Start Date</label>
                  <input type="date" name="start_date" class="form-control" <?= $is_signed?'readonly':'' ?>
                         value="<?= $is_edit ? $contract->start_date : date('Y-m-d') ?>">
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label class="xb-flabel">End Date</label>
                  <input type="date" name="end_date" class="form-control" <?= $is_signed?'readonly':'' ?>
                         value="<?= $is_edit ? ($contract->end_date??'') : '' ?>">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Linked Purchase Order</label>
                  <select name="pur_order" class="form-control" <?= $is_signed?'disabled':'' ?>>
                    <option value="">— None —</option>
                    <?php foreach ($purchase_orders as $po): ?>
                    <option value="<?=$po->id?>" <?= $is_edit && isset($contract->pur_order) && $contract->pur_order==$po->id?'selected':'' ?>><?= htmlspecialchars($po->pur_order_number) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group">
                  <label class="xb-flabel">Payment Terms</label>
                  <input type="text" name="payment_terms" class="form-control" <?= $is_signed?'readonly':'' ?>
                         value="<?= $is_edit ? htmlspecialchars($contract->payment_terms??'') : '' ?>" placeholder="e.g. 30 days net">
                </div>
              </div>
            </div>
          </div></div>
        </div>

        <div class="tab-pane" id="con-terms">
          <div class="xb-fcard"><div class="xb-fcard-header">Terms &amp; Contract Content</div><div class="xb-fcard-body">
            <div class="form-group">
              <label class="xb-flabel">Contract Content / Terms</label>
              <textarea name="content" class="form-control" rows="12" <?= $is_signed?'readonly':'' ?>
                        placeholder="Enter the full contract terms and conditions here..."><?= $is_edit ? htmlspecialchars($contract->content??'') : '' ?></textarea>
            </div>
            <div class="form-group">
              <label class="xb-flabel">Internal Note</label>
              <textarea name="note" class="form-control" rows="3" <?= $is_signed?'readonly':'' ?>
                        placeholder="Internal notes (not shown on contract)"><?= $is_edit ? htmlspecialchars($contract->note??'') : '' ?></textarea>
            </div>
          </div></div>
        </div>

        <?php if ($is_signed): ?>
        <div class="tab-pane" id="con-sig">
          <div class="xb-fcard"><div class="xb-fcard-header"><i class="fa fa-check-circle text-success"></i> Signature Record</div><div class="xb-fcard-body">
            <div class="row">
              <div class="col-md-4">
                <p class="xb-flabel">Signed By</p>
                <p style="font-size:16px;font-weight:700;"><?= htmlspecialchars($contract->signer_name??'Unknown') ?></p>
              </div>
              <div class="col-md-4">
                <p class="xb-flabel">Date Signed</p>
                <p style="font-size:16px;font-weight:700;"><?= $contract->signed_date ? _d($contract->signed_date) : '—' ?></p>
              </div>
              <div class="col-md-4">
                <p class="xb-flabel">Status</p>
                <span class="xb-status-badge signed"><i class="fa fa-check"></i> <?= htmlspecialchars($contract->signed_status ?? 'Signed') ?></span>
              </div>
            </div>
            <?php if (!empty($contract->signature)): ?>
            <div style="margin-top:16px;padding:16px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;">
              <p class="xb-flabel">Signature Image</p>
              <img src="<?= $contract->signature ?>" style="max-height:80px;border:1px solid #ddd;border-radius:4px;padding:4px;background:#fff;">
            </div>
            <?php endif; ?>
          </div></div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="xb-form-sidebar">
      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Document Summary</div>
        <div class="xb-sb-row"><span class="xb-sb-label">Status</span>
          <span class="xb-sb-value">
            <?php if ($is_signed): ?>
              <span class="xb-status-badge signed"><i class="fa fa-check"></i> Signed</span>
            <?php elseif ($is_edit): ?>
              <span class="xb-status-badge pending">Unsigned</span>
            <?php else: ?>
              <span class="xb-status-badge draft">New Draft</span>
            <?php endif; ?>
          </span>
        </div>
        <div class="xb-sb-row"><span class="xb-sb-label">Vendor</span>
          <span class="xb-sb-value"><?= $is_edit ? htmlspecialchars($contract->vendor_name??'—') : '—' ?></span>
        </div>
        <div class="xb-sb-row"><span class="xb-sb-label">Value</span>
          <span class="xb-sb-value" style="color:#1a6b3a;"><?= $is_edit ? xb_format_money($contract->contract_value) : '—' ?></span>
        </div>
        <div class="xb-sb-row"><span class="xb-sb-label">Start</span>
          <span class="xb-sb-value"><?= $is_edit && $contract->start_date ? _d($contract->start_date) : '—' ?></span>
        </div>
        <div class="xb-sb-row"><span class="xb-sb-label">Expires</span>
          <span class="xb-sb-value"><?php
            if ($is_edit && $contract->end_date) {
              $expired = strtotime($contract->end_date) < time();
              echo '<span' . ($expired ? ' class="text-danger"' : '') . '>' . _d($contract->end_date) . '</span>';
              if ($expired) echo '<br><small class="text-danger">Expired</small>';
            } else { echo '—'; }
          ?></span>
        </div>
        <?php if ($is_signed): ?>
        <div class="xb-sb-row"><span class="xb-sb-label">Signed by</span>
          <span class="xb-sb-value"><?= htmlspecialchars($contract->signer_name??'—') ?></span>
        </div>
        <?php endif; ?>
      </div>

      <div class="xb-sb-box">
        <div class="xb-sb-box-header">Purchase Workflow</div>
        <a href="<?= admin_url('xetuu_books/purchase_requests') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>Purchase Requests</a>
        <a href="<?= admin_url('xetuu_books/purchase_orders') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>Purchase Orders</a>
        <a href="<?= admin_url('xetuu_books/purchase_invoices') ?>" class="xb-sb-nav-link"><svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6z"/></svg>Purchase Invoices</a>
        <a href="<?= admin_url('xetuu_books/purchase_contracts') ?>" class="xb-sb-nav-link active"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2z"/></svg>Contracts</a>
      </div>
    </div>
  </div>
  </form>
</div>
