<?php
/**
 * Enterprise Attachments Panel
 *
 * Expected variables:
 *   $attachment_doc_type  string  e.g. 'move', 'budget', 'asset'
 *   $attachment_doc_id    int     document ID (0 = new, upload disabled)
 *   $attachment_base_url  string  base AJAX URL
 */
defined('BASEPATH') or exit('No direct script access allowed');

$_doc_type = $attachment_doc_type ?? '';
$_doc_id   = (int)($attachment_doc_id ?? 0);
$_base_url = $attachment_base_url ?? admin_url('xetuu_books/ajax');
$_allowed  = 'PDF, DOCX, XLSX, PNG, JPG, XML';
?>

<div class="xb-collapsible-section" style="margin-bottom:0;">
    <div class="xb-section-header">
        <span><i class="fa fa-paperclip"></i> Attachments</span>
        <i class="fa fa-chevron-down xb-collapse-icon"></i>
    </div>
    <div class="xb-section-body">

        <?php if (!$_doc_id): ?>
        <p class="text-muted text-center" style="padding:10px;">Save this document first to attach files.</p>
        <?php else: ?>

        <div id="xb-attachments-panel"
             data-doc-type="<?php echo htmlspecialchars($_doc_type); ?>"
             data-doc-id="<?php echo $_doc_id; ?>"
             data-base-url="<?php echo $_base_url; ?>">

            <!-- Drop zone -->
            <div class="xb-drop-zone" id="xb-drop-zone">
                <i class="fa fa-cloud-upload"></i>
                <div style="font-size:14px; font-weight:600; margin-bottom:4px;">
                    Drag &amp; drop files here, or click to browse
                </div>
                <div style="font-size:12px;">Supported: <?php echo $_allowed; ?></div>
                <input type="file" id="xb-file-input" class="xb-file-input" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.xml"
                       style="display:none;">
            </div>

            <!-- Attachment list -->
            <div id="xb-attachments-list" style="margin-top:14px;">
                <p class="text-muted text-center"><i class="fa fa-spinner fa-spin"></i> Loading…</p>
            </div>
        </div>

        <script>
        $(function () {
            var baseUrl = '<?php echo addslashes($_base_url); ?>';
            var docType = '<?php echo addslashes($_doc_type); ?>';
            var docId   = <?php echo $_doc_id; ?>;
            XBWorkspace.initAttachments(baseUrl, docType, docId);
        });
        </script>

        <?php endif; ?>
    </div>
</div>
