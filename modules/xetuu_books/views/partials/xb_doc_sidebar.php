<?php
/**
 * Enterprise Document Sidebar Partial
 *
 * Expected variables:
 *   $sidebar_amount        float   primary balance/total amount to display large
 *   $sidebar_amount_label  string  label below the amount, e.g. "Invoice Total"
 *   $sidebar_balance       float   optional secondary amount (balance due)
 *   $sidebar_balance_label string  label for balance
 *   $sidebar_summary       array   [['label'=>'', 'value'=>'', 'class'=>'']]  document summary rows
 *   $sidebar_doc_info      array   [['label'=>'', 'value'=>'']]  creation/audit info
 *   $sidebar_workflow      array   [['label'=>'', 'value'=>'']]  approval/workflow info
 *   $sidebar_quick_links   array   [['label'=>'', 'url'=>'', 'icon'=>'fa-...']]
 */
defined('BASEPATH') or exit('No direct script access allowed');

$_amount        = (float)($sidebar_amount        ?? 0);
$_amount_label  = $sidebar_amount_label  ?? 'Total';
$_balance       = isset($sidebar_balance) ? (float)$sidebar_balance : null;
$_balance_label = $sidebar_balance_label ?? 'Balance Due';
$_summary       = $sidebar_summary       ?? [];
$_doc_info      = $sidebar_doc_info      ?? [];
$_workflow      = $sidebar_workflow      ?? [];
$_quick_links   = $sidebar_quick_links   ?? [];
?>

<div class="xb-ew-sidebar">

    <!-- Amount summary card -->
    <div class="xb-sb-block" style="text-align:center; padding: 18px 16px;">
        <div class="xb-sb-amount"><?php echo xb_format_money($_amount); ?></div>
        <div class="xb-sb-amount-label"><?php echo htmlspecialchars($_amount_label); ?></div>
        <?php if ($_balance !== null): ?>
        <hr style="margin: 8px 0; border-color: #f3f4f6;">
        <div class="xb-info-row">
            <span class="xb-info-label"><?php echo htmlspecialchars($_balance_label); ?></span>
            <span class="xb-info-val <?php echo $_balance > 0 ? 'text-danger' : 'text-success'; ?>">
                <?php echo xb_format_money($_balance); ?>
            </span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Document summary -->
    <?php if (!empty($_summary)): ?>
    <div class="xb-sb-block">
        <h4>Document Summary</h4>
        <?php foreach ($_summary as $_row): ?>
        <div class="xb-info-row">
            <span class="xb-info-label"><?php echo htmlspecialchars($_row['label']); ?></span>
            <span class="xb-info-val <?php echo htmlspecialchars($_row['class'] ?? ''); ?>">
                <?php echo $_row['value']; ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Document information (audit trail) -->
    <?php if (!empty($_doc_info)): ?>
    <div class="xb-sb-block">
        <h4>Document Information</h4>
        <?php foreach ($_doc_info as $_row): ?>
        <div class="xb-info-row">
            <span class="xb-info-label"><?php echo htmlspecialchars($_row['label']); ?></span>
            <span class="xb-info-val"><?php echo htmlspecialchars($_row['value'] ?? '—'); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Workflow / approval -->
    <?php if (!empty($_workflow)): ?>
    <div class="xb-sb-block">
        <h4>Workflow</h4>
        <?php foreach ($_workflow as $_row): ?>
        <div class="xb-info-row">
            <span class="xb-info-label"><?php echo htmlspecialchars($_row['label']); ?></span>
            <span class="xb-info-val"><?php echo $_row['value']; ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Quick links -->
    <?php if (!empty($_quick_links)): ?>
    <div class="xb-sb-block">
        <h4>Quick Links</h4>
        <div class="xb-quick-links">
            <?php foreach ($_quick_links as $_lnk): ?>
            <a href="<?php echo $_lnk['url']; ?>">
                <i class="fa <?php echo htmlspecialchars($_lnk['icon'] ?? 'fa-link'); ?>"></i>
                <?php echo htmlspecialchars($_lnk['label']); ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
