<?php
/**
 * Enterprise Document Toolbar Partial
 *
 * Expected variables (set before loading this view):
 *   $toolbar_breadcrumbs  array of ['label'=>'', 'url'=>''] — last item has no url
 *   $toolbar_title        string  document title
 *   $toolbar_state        string  state key (draft|posted|cancel|paid|partial|overdue…)
 *   $toolbar_payment_state string optional payment state
 *   $toolbar_actions      string  HTML of action buttons (right side)
 *   $toolbar_doc_id       int     document id (0 for new)
 *   $toolbar_form_id      string  CSS selector of the form for auto-save, e.g. '#invoice-form'
 *   $toolbar_autosave_url string  URL to POST form data for auto-save
 *   $toolbar_doc_type     string  e.g. 'move', 'budget', 'asset'
 */
defined('BASEPATH') or exit('No direct script access allowed');

$_breadcrumbs    = $toolbar_breadcrumbs    ?? [];
$_title          = $toolbar_title          ?? 'Document';
$_state          = $toolbar_state          ?? 'draft';
$_payment_state  = $toolbar_payment_state  ?? null;
$_actions        = $toolbar_actions        ?? '';
$_doc_id         = (int)($toolbar_doc_id   ?? 0);
$_form_id        = $toolbar_form_id        ?? '#xb-main-form';
$_autosave_url   = $toolbar_autosave_url   ?? '';
$_doc_type       = $toolbar_doc_type       ?? 'document';
?>

<!-- Breadcrumb -->
<div class="xb-breadcrumb">
    <?php foreach ($_breadcrumbs as $i => $_bc): ?>
        <?php if ($i > 0): ?><span class="xb-bc-sep">&rsaquo;</span><?php endif; ?>
        <?php if (!empty($_bc['url'])): ?>
            <a href="<?php echo $_bc['url']; ?>"><?php echo htmlspecialchars($_bc['label']); ?></a>
        <?php else: ?>
            <span class="xb-bc-current"><?php echo htmlspecialchars($_bc['label']); ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- Header Toolbar -->
<div class="xb-header-toolbar">
    <div class="xb-header-title">
        <h3><?php echo htmlspecialchars($_title); ?></h3>
        <?php echo xb_status_badge($_state, $_payment_state); ?>
    </div>
    <div class="xb-header-actions">
        <span id="xb-save-indicator" style="display:none;"></span>
        <?php echo $_actions; ?>
    </div>
</div>

<?php if ($_doc_id && $_autosave_url): ?>
<script>
$(function () {
    XBWorkspace.initAutoSave({
        url:      '<?php echo addslashes($_autosave_url); ?>',
        formId:   '<?php echo addslashes($_form_id); ?>',
        docId:    <?php echo $_doc_id; ?>,
        docType:  '<?php echo addslashes($_doc_type); ?>',
        interval: 30000
    });
});
</script>
<?php endif; ?>
